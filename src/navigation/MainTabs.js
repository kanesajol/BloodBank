import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { CommonActions } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import DonorSearchScreen from '../screens/DonorSearchScreen';
import DonorDetailScreen from '../screens/DonorDetailScreen';
import RegisterDonorScreen from '../screens/RegisterDonorScreen';
import ConsumptionScreen from '../screens/ConsumptionScreen';
import AddConsumptionScreen from '../screens/AddConsumptionScreen';
import ReportsScreen from '../screens/ReportsScreen';
import ReturningDonorScreen from '../screens/ReturningDonorScreen';
import StaffScreen from '../screens/StaffScreen';
import StaffLogScreen from '../screens/StaffLogScreen';
import StaffProfileScreen from '../screens/StaffProfileScreen';

const Tab = createBottomTabNavigator();
const CollectionStack = createNativeStackNavigator();
const ConsumptionStack = createNativeStackNavigator();
const StaffStack = createNativeStackNavigator();

const TAB_ICONS = {
  CollectionTab: '♥',
  ConsumptionTab: '▤',
  ReportsTab: '▥',
  StaffTab: '♙',
};

function EvenTabBar({ state, descriptors, navigation }) {
  const insets = useSafeAreaInsets();

  const tabs = state.routes.map((route, index) => {
    const focused = state.index === index;
    const color = focused ? '#990000' : '#8e8e93';
    const label = descriptors[route.key].options.title ?? route.name;

    const onPress = () => {
      const event = navigation.emit({
        type: 'tabPress',
        target: route.key,
        canPreventDefault: true,
      });

      if (!focused && !event.defaultPrevented) {
        navigation.dispatch({
          ...CommonActions.navigate(route.name),
          target: state.key,
        });
      }
    };

    return (
      <Pressable
        key={route.key}
        accessibilityRole="button"
        accessibilityState={focused ? { selected: true } : {}}
        onPress={onPress}
        style={styles.item}
      >
        <Text allowFontScaling={false} style={[styles.icon, { color }]}>{TAB_ICONS[route.name] ?? '•'}</Text>
        <Text allowFontScaling={false} style={[styles.label, { color }]} numberOfLines={1}>
          {label}
        </Text>
      </Pressable>
    );
  });

  return (
    <View style={[styles.bar, { paddingBottom: Math.max(insets.bottom, 8) }]}>
      <View style={styles.gap} />
      {tabs.flatMap((tab, index) => (index === 0 ? [tab] : [<View key={`${tab.key}-gap`} style={styles.gap} />, tab]))}
      <View style={styles.gap} />
    </View>
  );
}

function CollectionNavigator() {
  return (
    <CollectionStack.Navigator>
      <CollectionStack.Screen
        name="DonorSearch"
        component={DonorSearchScreen}
        options={{ title: '', headerShown: true }}
      />
      <CollectionStack.Screen name="DonorDetail" component={DonorDetailScreen} />
      <CollectionStack.Screen
        name="RegisterDonor"
        component={RegisterDonorScreen}
        options={{ title: 'Register donor' }}
      />
      <CollectionStack.Screen
        name="ReturningDonor"
        component={ReturningDonorScreen}
        options={{ title: 'Returning donor' }}
      />
    </CollectionStack.Navigator>
  );
}

function ConsumptionNavigator() {
  return (
    <ConsumptionStack.Navigator>
      <ConsumptionStack.Screen
        name="ConsumptionList"
        component={ConsumptionScreen}
        options={{ title: '', headerShown: true }}
      />
      <ConsumptionStack.Screen
        name="AddConsumption"
        component={AddConsumptionScreen}
        options={{ title: 'Add record' }}
      />
    </ConsumptionStack.Navigator>
  );
}

function StaffNavigator() {
  return (
    <StaffStack.Navigator>
      <StaffStack.Screen
        name="StaffList"
        component={StaffScreen}
        options={{ title: '' }}
      />
      <StaffStack.Screen name="StaffLog" component={StaffLogScreen} options={{ title: 'Staff log' }} />
      <StaffStack.Screen name="StaffProfile" component={StaffProfileScreen} options={{ title: 'My profile' }} />
    </StaffStack.Navigator>
  );
}

export default function MainTabs() {
  return (
    <Tab.Navigator
      tabBar={(props) => <EvenTabBar {...props} />}
      screenOptions={{ headerShown: false }}
    >
      <Tab.Screen name="CollectionTab" component={CollectionNavigator} options={{ title: 'Collection' }} />
      <Tab.Screen name="ConsumptionTab" component={ConsumptionNavigator} options={{ title: 'Consumption' }} />
      <Tab.Screen
        name="ReportsTab"
        component={ReportsScreen}
        options={{
          title: 'Reports',
          headerShown: true,
          headerTitle: '',
          headerStyle: { backgroundColor: '#fff' },
          headerTintColor: '#990000',
        }}
      />
      <Tab.Screen name="StaffTab" component={StaffNavigator} options={{ title: 'Staff' }} />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#fff',
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: '#e4e4e4',
    paddingTop: 8,
    width: '100%',
  },
  gap: {
    flexGrow: 1,
    flexShrink: 1,
    flexBasis: 0,
  },
  item: {
    alignItems: 'center',
    justifyContent: 'center',
    flexGrow: 0,
    flexShrink: 0,
  },
  icon: {
    fontSize: 18,
    lineHeight: 22,
    fontWeight: '700',
    textAlign: 'center',
  },
  label: {
    marginTop: 2,
    fontSize: 11,
    lineHeight: 14,
    fontWeight: '600',
    textAlign: 'center',
  },
});
