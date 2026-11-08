import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { Pressable, Text } from 'react-native';
import { useAuth } from '../context/AuthContext';
import DonorSearchScreen from '../screens/DonorSearchScreen';
import DonorDetailScreen from '../screens/DonorDetailScreen';
import RegisterDonorScreen from '../screens/RegisterDonorScreen';
import ConsumptionScreen from '../screens/ConsumptionScreen';
import AddConsumptionScreen from '../screens/AddConsumptionScreen';
import ReportsScreen from '../screens/ReportsScreen';
import StaffScreen from '../screens/StaffScreen';
import StaffLogScreen from '../screens/StaffLogScreen';

const Tab = createBottomTabNavigator();
const CollectionStack = createNativeStackNavigator();
const ConsumptionStack = createNativeStackNavigator();
const StaffStack = createNativeStackNavigator();

function SignOutButton() {
  const { signOut } = useAuth();
  return (
    <Pressable onPress={() => signOut()} style={{ marginRight: 8 }}>
      <Text style={{ color: '#990000', fontWeight: '700' }}>Sign out</Text>
    </Pressable>
  );
}

function CollectionNavigator() {
  return (
    <CollectionStack.Navigator>
      <CollectionStack.Screen
        name="DonorSearch"
        component={DonorSearchScreen}
        options={{
          title: 'Collection',
          headerRight: () => <SignOutButton />,
        }}
      />
      <CollectionStack.Screen name="DonorDetail" component={DonorDetailScreen} />
      <CollectionStack.Screen
        name="RegisterDonor"
        component={RegisterDonorScreen}
        options={{ title: 'Register donor' }}
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
        options={{ title: 'Consumption' }}
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
      <StaffStack.Screen name="StaffList" component={StaffScreen} options={{ title: 'Staff' }} />
      <StaffStack.Screen name="StaffLog" component={StaffLogScreen} options={{ title: 'Staff log' }} />
    </StaffStack.Navigator>
  );
}

export default function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#990000',
        headerShown: false,
      }}
    >
      <Tab.Screen name="CollectionTab" component={CollectionNavigator} options={{ title: 'Collection' }} />
      <Tab.Screen name="ConsumptionTab" component={ConsumptionNavigator} options={{ title: 'Consumption' }} />
      <Tab.Screen
        name="ReportsTab"
        component={ReportsScreen}
        options={{
          title: 'Reports',
          headerShown: true,
          headerStyle: { backgroundColor: '#fff' },
          headerTintColor: '#990000',
        }}
      />
      <Tab.Screen name="StaffTab" component={StaffNavigator} options={{ title: 'Staff' }} />
    </Tab.Navigator>
  );
}
